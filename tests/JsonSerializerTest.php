<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/20/15
 * Time: 9:04 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Tests\Json;

use DateTime;
use NilPortugues\Api\Json\JsonSerializer;
use NilPortugues\Api\Json\JsonTransformer;
use NilPortugues\Api\Mapping\Mapper;
use NilPortugues\Tests\Api\Json\Dummy\ComplexObject\Comment;
use NilPortugues\Tests\Api\Json\Dummy\ComplexObject\Post;
use NilPortugues\Tests\Api\Json\Dummy\ComplexObject\User;
use NilPortugues\Tests\Api\Json\Dummy\ComplexObject\ValueObject\CommentId;
use NilPortugues\Tests\Api\Json\Dummy\ComplexObject\ValueObject\PostId;
use NilPortugues\Tests\Api\Json\Dummy\ComplexObject\ValueObject\UserId;

class JsonSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Post
     */
    private function getPostObject()
    {
        $post = new Post(
            new PostId(9),
            'Hello World',
            'Your first post',
            new User(
                new UserId(1),
                'Post Author'
            ),
            [
                new Comment(
                    new CommentId(1000),
                    'Have no fear, sers, your king is safe.',
                    new User(new UserId(2), 'Barristan Selmy'),
                    [
                        'created_at' => (new DateTime(
                                '2015-07-18 12:13',
                                new \DateTimeZone('Europe/Madrid')
                            ))->format('c'),
                        'accepted_at' => (new DateTime(
                                '2015-07-19 00:00',
                                new \DateTimeZone('Europe/Madrid')
                            ))->format('c'),
                    ]
                ),
            ]
        );

        return $post;
    }

    /**
     *
     */
    public function testItWillRenamePropertiesAndHideFromClass()
    {
        $mappings = $this->mappings();

        $expected = <<<JSON
{
  "post_id": 9,
  "headline": "Hello World",
  "body": "Your first post",
  "author": {
    "user_id": 1,
    "name": "Post Author"
  },
  "comments": [
    {
      "comment_id": 1000,
      "dates": {
        "created_at": "2015-07-18T12:13:00+02:00",
        "accepted_at": "2015-07-19T00:00:00+02:00"
      },
      "comment": "Have no fear, sers, your king is safe.",
      "user": {
        "user_id": 2,
        "name": "Barristan Selmy"
      }
    }
  ],
  "links": {
    "self": {
      "href": "http://example.com/posts/9"
    },
    "comments": {
      "href": "http://example.com/posts/9/comments"
    }
  }
}
JSON;


        $this->assertEquals(
            json_decode($expected, true),
            json_decode((new JsonSerializer(new Mapper($mappings)))->serialize($this->getPostObject()), true)
        );
    }

    public function testItCanSerializeArrays()
    {
        $mappings = $this->mappings();

        $expected = <<<JSON
[
  {
    "post_id": 9,
    "headline": "Hello World",
    "body": "Your first post",
    "author": {
      "user_id": 1,
      "name": "Post Author"
    },
    "comments": [
      {
        "comment_id": 1000,
        "dates": {
          "created_at": "2015-07-18T12:13:00+02:00",
          "accepted_at": "2015-07-19T00:00:00+02:00"
        },
        "comment": "Have no fear, sers, your king is safe.",
        "user": {
          "user_id": 2,
          "name": "Barristan Selmy"
        }
      }
    ],
    "links": {
      "self": {
        "href": "http://example.com/posts/9"
      },
      "comments": {
        "href": "http://example.com/posts/9/comments"
      }
    }
  },
  {
    "post_id": 9,
    "headline": "Hello World",
    "body": "Your first post",
    "author": {
      "user_id": 1,
      "name": "Post Author"
    },
    "comments": [
      {
        "comment_id": 1000,
        "dates": {
          "created_at": "2015-07-18T12:13:00+02:00",
          "accepted_at": "2015-07-19T00:00:00+02:00"
        },
        "comment": "Have no fear, sers, your king is safe.",
        "user": {
          "user_id": 2,
          "name": "Barristan Selmy"
        }
      }
    ],
    "links": {
      "self": {
        "href": "http://example.com/posts/9"
      },
      "comments": {
        "href": "http://example.com/posts/9/comments"
      }
    }
  }
]
JSON;
        $serializer = (new JsonSerializer(new Mapper($mappings)));

        $this->assertEquals(
            json_decode($expected, true),
            json_decode($serializer->serialize([$this->getPostObject(), $this->getPostObject()]), true)
        );
    }

    public function testGetTransformer()
    {
        $serializer = (new JsonSerializer(new Mapper([])));

        $this->assertInstanceOf(JsonTransformer::class, $serializer->getTransformer());
    }


    /**
     * @return array
     */
    protected function mappings()
    {
        return [
            [
                'class' => Post::class,
                'alias' => 'Message',
                'aliased_properties' => [
                    'author' => 'author',
                    'title' => 'headline',
                    'content' => 'body',
                ],
                'hide_properties' => [
                ],
                'id_properties' => [
                    'postId',
                ],
                'urls' => [
                    // Mandatory
                    'self' => 'http://example.com/posts/{postId}',
                    // Optional
                    'comments' => 'http://example.com/posts/{postId}/comments',
                ],
            ],
            [
                'class' => User::class,
                'alias' => '',
                'aliased_properties' => [],
                'hide_properties' => [],
                'id_properties' => [
                    'userId',
                ],
                'urls' => [
                    'self' => 'http://example.com/users/{userId}',
                    'friends' => 'http://example.com/users/{userId}/friends',
                    'comments' => 'http://example.com/users/{userId}/comments',
                ],
            ],
            [
                'class' => Comment::class,
                'alias' => '',
                'aliased_properties' => [],
                'hide_properties' => [],
                'id_properties' => [
                    'commentId',
                ],
                'urls' => [
                    'self' => 'http://example.com/comments/{commentId}',
                ],
            ],
            [
                'class' => CommentId::class,
                'alias' => '',
                'aliased_properties' => [],
                'hide_properties' => [],
                'id_properties' => [
                    'commentId',
                ],
                'urls' => [
                    'self' => 'http://example.com/comments/{commentId}',
                ],
            ],
        ];
    }
}
